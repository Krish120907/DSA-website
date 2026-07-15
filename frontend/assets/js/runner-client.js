// Frontend communication handler with local-runner and remote backend api
const RunnerClient = {
    // Send code to the local python compiler runner
    async executeLocally(code, testCases, timeout = 2.0) {
        try {
            const response = await fetch(RUNNER_URL, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    code: code,
                    tests: testCases,
                    timeout: timeout
                })
            });

            if (!response.ok) {
                throw new Error(`Runner server responded with status: ${response.status}`);
            }

            return await response.json();
        } catch (error) {
            console.error('Local compilation service unavailable:', error);
            return {
                success: false,
                error: 'Could not connect to local runner. Make sure "python3 local-runner/runner.py" is running on port 8000.'
            };
        }
    },

    // Submit evaluation results to the main backend server
    async submitResultToBackend(problemId, status, passedCount, totalCount, runtimeMs, code) {
        const payload = {
            problem_id: problemId,
            status: status, // e.g. "Accepted", "Wrong Answer", "Runtime Error", "Compilation Error"
            passed_count: passedCount,
            total_count: totalCount,
            runtime_ms: runtimeMs,
            code: code
        };

        try {
            const response = await fetch(`${API_BASE_URL}/submissions`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    ...Auth.getAuthHeader()
                },
                body: JSON.stringify(payload)
            });

            if (!response.ok) {
                throw new Error(`Backend server responded with status: ${response.status}`);
            }

            return await response.json();
        } catch (error) {
            console.error('Submission reporting failed:', error);
            return {
                success: false,
                error: 'Submission processed locally but could not upload to remote backend.'
            };
        }
    }
};
